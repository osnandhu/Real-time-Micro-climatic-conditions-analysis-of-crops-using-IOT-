/* The real time moving average code is used here for the calculating the average of  moisture outputs dynamically. Here the moving average is taken for a particular interval and sensor characteristics are observed.*/
MATLAB CODE:
a = arduino;
v = readVoltage(a,'A0');

ii = 0;
Moist = zeros(1e4,1);
t = zeros(1e4,1);

tic
while toc < 10
    ii = ii + 1;
    % Read current voltage value
    v = readVoltage(a,'A0');
    % Calculate moisture
    Moist = (v/3.3)*100;
    % Get time since starting
    t(ii) = toc;
end

t = t(1:ii);
%Get frequency
timeBetweenDataPoints = diff(t);
averageTimePerDataPoint = mean(timeBetweenDataPoints);
dataRateHz = 1/averageTimePerDataPoint;
fprintf('Acquired one data point per %.3f seconds (%.f Hz)\n',...
    averageTimePerDataPoint,dataRateHz)


figure
h = animatedline;
ax = gca;
ax.YGrid = 'on';
%window = 5;
startTime = datetime('now');
%i = 0;
stop=false;
while ~stop
    v = readVoltage(a,'A0');
    Moist = (v/3.3)*100;
    t =  datetime('now') - startTime;
    addpoints(h,datenum(t),Moist)

    ax.XLim = datenum([t-seconds(15) t]);
    datetick('x','keeplimits')
    drawnow
    %i=i+1;
    stop = readDigitalPin(a,'D7');
end

%timeBetweenDataPoints = diff(t);
%averageTimePerDataPoint = timeBetweenDataPoints/2;
%dataRateHz = 1/averageTimePerDataPoint;
%fprintf('Acquired one data point per %.3f seconds (%.f Hz)\n',averageTimePerDataPoint,dataRateHz)

[timeLogs,moistLogs] = getpoints(h);
timeSecs = (timeLogs-timeLogs(1))*24*3600;
figure
plot(timeSecs,moistLogs)
title('Logged Data')
xlabel('Elapsed time (sec)')
ylabel('Moisture (in Percentage)')

smoothMoist1 = smooth(moistLogs,50);
smoothMoist2 = smooth(moistLogs,500);

figure
plot(timeSecs,moistLogs,timeSecs,smoothMoist1,'r',timeSecs,smoothMoist2,'g')
title('Logged Data with Moving Avg.')
xlabel('Elapsed time (sec)')
ylabel('Moisture (in Percentage)')
hold on
legend ('Read Data','MAW 50','MAW 500')// windows of sizes 50 and 500 are taken. It is observed that for 500 the ouput graph is smoother than that of 50.

Fs = dataRateHz;
NFFT = length(moistLogs);
F = (0 : 1/NFFT : 1/2-1/NFFT)*Fs;

MOIST = fft(moistLogs,NFFT);
MOIST(1) = 0;

phaseMoist = unwrap(angle(MOIST));
helperFrequencyAnalysisPlot2(F,abs(MOIST(1:NFFT/2)),...
  'Frequency','Magnitude','Frequency Domain Analysis')
helperFrequencyAnalysisPlot1(F,abs(MOIST(1:NFFT/2)),phaseMoist,NFFT)
